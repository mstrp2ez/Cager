var SkyNet=function(p_Host){
	Chat.AddMessage("Connecting..");
	this.m_Socket=new WebSocket(p_Host);
	this.m_Loggedin=false;
	this.m_Closed=false;
	this.m_DataReader=new FileReader();
	this.m_SendReader=new FileReader();
	this.m_Busy=false;
	this.m_SnapshotRate=0;
	this.m_AppStart=Date.now();
	this.m_SnapshotBuffer=[];
	this.m_SnapshotID=0;
	this.m_SnapshotsRecieved=0;
	this.m_SnapshotsSent=0;
	this.m_LastUpdate=0;
	this.debug=true;
	var s=this.m_Socket;
	var xThis=this;

	
	this.onMessage=function(p_Data){
	//this.m_DataReader.onload=function(){
		var view=new Uint8Array(p_Data.currentTarget.result);
		var appOpCode=view[0];
		var data=view.subarray(1);  //cg monster
		if(xThis.m_Loggedin===false){
			if(appOpCode!=0x02){return;}
			xThis.HandleLogin(data);
			xThis.m_Busy=false;
			return;
		}
		if(appOpCode==0x01){
			var textBlob=new Blob([data]);
			var fr=new FileReader();
			fr.onload=function(e){
				var message=e.target.result;
				document.dispatchEvent(CreateCustomEvent('NetMessage',message));
				fr.onload=0;
				fr=0;
			}
			fr.readAsText(textBlob);
		}
		if(appOpCode==0x03||appOpCode==0x08){ //snapshot
			var obj={d:data,t:Date.now()};
			
			document.dispatchEvent(CreateCustomEvent('NetSnapshot',obj));
		}
		if(appOpCode==0x05){//player disconnect
			var obj={d:data}
			document.dispatchEvent(CreateCustomEvent('NetPlayerDisconnect',obj));
		}
		p_Data.currentTarget.onload=0;
		p_Data.currentTarget=0;
		xThis.m_Busy=false;
	}
	this.onSendAsString=function(e){
		var buff=new Uint8Array(e.target.result);
		buff[0]=buff[0]-48; //number hack TODO: fix this so that it is always the right value
		if(buff.length<=1){return;}

		xThis.m_SnapshotsSent++;
		s.send(buff.buffer); 
		e.target.onload=0;
		e.target=0;
	}
	this.onSend=function(e){
//	this.m_SendReader.onload=function(e){
		var buff=new Uint8Array(e.target.result);
		if(buff.length<=1){return;}
		var iC=buff.length;
		for(var i=0;i<iC;i++){
			buff[i]=buff[i]-48;
		}
		
		xThis.m_SnapshotsSent++;
		s.send(buff.buffer); 
		e.target.onload=0;
		e.target=0;
	}
	s.onopen=function(e){
		var evnt=CreateCustomEvent('NetConnect',e);
		document.dispatchEvent(evnt);
		Chat.AddMessage("Connected");
	}
	s.onclose=function(e){
		var evnt=CreateCustomEvent('NetDisconnect',e);
		document.dispatchEvent(evnt);
		Chat.AddMessage("Disconnected");
	}
	s.onmessage=function(p_msg){
		if(xThis.m_Closed){return;}
		xThis.m_SnapshotsRecieved++;
		
		var nfr=new FileReader();
		nfr.onload=xThis.onMessage;
		nfr.readAsArrayBuffer(p_msg.data);
	}
	this.Update=function(p_Delta){
		if(p_Delta-xThis.m_LastUpdate>1000){
			xThis.m_LastUpdate=p_Delta;
			var pr=xThis.m_SnapshotsRecieved;
			xThis.m_SnapshotsRecieved=0;
			console.log('Packet rate: '+pr);
		}
	}
	this.HandleLogin=function(msg){
		var blob=new Blob([msg]);
		var fr=new FileReader();
		fr.onload=function(e){
			var view=new Uint8Array(fr.result);
			var msg=String.fromCharCode(view[0])+String.fromCharCode(view[1]);
			if(msg=="ok"){
				var offset=2;
				var entityID=view[offset++];
				entityID=(entityID<<8)|view[offset++];
				entityID=(entityID<<16)|view[offset++];
				entityID=(entityID<<24)|view[offset++];
				
				xThis.m_Loggedin=true;
				Chat.AddMessage("Logged in");
				document.dispatchEvent(CreateCustomEvent('NetLoggedIn',{playerid:entityID}));
			}
			fr.onload=0;
			fr=0;
		}
		fr.readAsArrayBuffer(blob);
	}
	this.SendAsString=function(p_Data){
		var bb=new Blob([p_Data],{'type': 'application/type'});
		var sfr=new FileReader();
		sfr.onload=xThis.onSendAsString;
		sfr.readAsArrayBuffer(bb);
	}
	this.Send=function(p_data){
		var buff=new Uint8Array(p_data.length);
		for(var i=0;i<p_data.length;i++){
			buff[i]=p_data[i];
		}
		xThis.m_SnapshotsSent++
		//EntityMngr.SaveState();
		s.send(buff.buffer); 
	}
	this.Disconnect=function(){
		if(xThis.m_Closed){return;}
		xThis.m_Closed=true;
		s.close();
		Chat.AddMessage("Connection closing, refreshing page in 2 seconds");
		setTimeout(function(){
			location.reload();
		},2000);
		
	}
	//this.Update();
}
window.SkyNet=new SkyNet("ws://83.253.155.170:10444");